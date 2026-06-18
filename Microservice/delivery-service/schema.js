const { buildSchema } = require('graphql');

const schema = buildSchema(`
    type Delivery {
        id: ID!
        order_id: Int!
        customer_name: String!
        address: String!
        status: String!
        created_at: String!
    }

    type Query {
        getDeliveries: [Delivery]
        getDelivery(id: ID!): Delivery
    }

    type Mutation {
        updateDeliveryStatus(id: ID!, status: String!): Delivery
        deleteDelivery(id: ID!): String
    }
`);

module.exports = schema;
